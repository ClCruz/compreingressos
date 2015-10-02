/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.compreingressos.knowledge.model;

import java.io.Serializable;
import javax.persistence.Basic;
import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;
import javax.persistence.JoinColumn;
import javax.persistence.ManyToOne;
import javax.persistence.NamedQueries;
import javax.persistence.NamedQuery;
import javax.persistence.Table;
import javax.validation.constraints.NotNull;
import javax.xml.bind.annotation.XmlRootElement;

/**
 *
 * @author edicarlos.barbosa
 */
@Entity
@Table(name = "DIM_KBASE_FUSI_USUA")
@XmlRootElement
@NamedQueries({
    @NamedQuery(name = "FusiUsua.findAll", query = "SELECT f FROM FusiUsua f"),
    @NamedQuery(name = "FusiUsua.findByUsuarioEFuncao", query = "SELECT MAX(f.id) FROM FusiUsua f WHERE f.funcaoSistema.id = :fusiId AND f.usuario.id = :usuaId"),
    @NamedQuery(name = "FusiUsua.findSelectedByUsuario", query = "SELECT f FROM FusiUsua f WHERE f.usuario = :usuaId"),
    @NamedQuery(name = "FusiUsua.findAllByUsuario", query = "SELECT f FROM FuncaoSistema f WHERE f.ativo = true")
})
public class FusiUsua implements Serializable {
    private static final long serialVersionUID = 1L;
    @Id
    @Basic(optional = false)
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "ID_FUUS")
    private Integer id;
    @JoinColumn(name = "ID_USUA", referencedColumnName = "ID_USUA")
    @ManyToOne(optional = false)
    private Usuario usuario;
    @JoinColumn(name = "ID_FUSI", referencedColumnName = "ID_FUSI")
    @ManyToOne(optional = false)
    private FuncaoSistema funcaoSistema;

    public FusiUsua() {
    }

    public FusiUsua(FuncaoSistema funcaoSistema, Usuario usuario) {
        this.usuario = usuario;
        this.funcaoSistema = funcaoSistema;
    }        

    public FusiUsua(Integer id) {
        this.id = id;
    }

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public Usuario getUsuario() {
        return usuario;
    }

    public void setUsuario(Usuario usuario) {
        this.usuario = usuario;
    }

    public FuncaoSistema getFuncaoSistema() {
        return funcaoSistema;
    }

    public void setFuncaoSistema(FuncaoSistema funcaoSistema) {
        this.funcaoSistema = funcaoSistema;
    }

    @Override
    public int hashCode() {
        int hash = 0;
        hash += (id != null ? id.hashCode() : 0);
        return hash;
    }

    @Override
    public boolean equals(Object object) {
        // TODO: Warning - this method won't work in the case the id fields are not set
        if (!(object instanceof FusiUsua)) {
            return false;
        }
        FusiUsua other = (FusiUsua) object;
        if ((this.id == null && other.id != null) || (this.id != null && !this.id.equals(other.id))) {
            return false;
        }
        return true;
    }

    @Override
    public String toString() {
        return id.toString();
    }
    
}
