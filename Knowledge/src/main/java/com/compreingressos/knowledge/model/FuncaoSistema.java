/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.compreingressos.knowledge.model;

import java.io.Serializable;
import java.util.Collection;
import java.util.List;
import javax.persistence.Basic;
import javax.persistence.CascadeType;
import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;
import javax.persistence.JoinColumn;
import javax.persistence.ManyToOne;
import javax.persistence.NamedQueries;
import javax.persistence.NamedQuery;
import javax.persistence.OneToMany;
import javax.persistence.Table;
import javax.persistence.Transient;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Size;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlTransient;

/**
 *
 * @author edicarlos.barbosa
 */
@Entity
@Table(name = "DIM_KBASE_FUNCAO_SISTEMA")
@XmlRootElement
@NamedQueries({
    @NamedQuery(name = "FuncaoSistema.findAll", query = "SELECT f FROM FuncaoSistema f ORDER BY f.descricao"),
    @NamedQuery(name = "FuncaoSistema.findPai", 
            query = "SELECT f FROM FuncaoSistema f INNER JOIN f.listaFuncaoUsuario fu "
                    + "WHERE fu.usuario.id = :usuarioId AND f.ativo = true "
                    + "ORDER BY f.ordemExibicao")
})
public class FuncaoSistema implements Serializable {
    private static final long serialVersionUID = 1L;
    @Id
    @Basic(optional = false)
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "ID_FUSI")
    private Integer id;
    @Basic(optional = false)
    @NotNull
    @Size(min = 1, max = 60)
    @Column(name = "DS_FUNCAO")
    private String descricao;
    @Basic(optional = false)
    @NotNull
    @Column(name = "IN_ORDEM_EXIBICAO")
    private int ordemExibicao;
    @Size(max = 255)
    @Column(name = "DS_URL")
    private String url;
    @Basic(optional = false)
    @NotNull
    @Column(name = "IN_ATIVO")
    private boolean ativo;    
    @JoinColumn(name = "IN_FUSI_PAI", referencedColumnName = "ID_FUSI")
    @ManyToOne
    private FuncaoSistema funcaoSistema;
    @OneToMany(mappedBy = "funcaoSistema")
    private Collection<FuncaoSistema> funcaoSistemaCollection;
    @OneToMany(cascade = CascadeType.ALL, mappedBy = "funcaoSistema")
    private List<FusiUsua> listaFuncaoUsuario;
    @Transient
    private boolean selected;
    @Transient
    private FusiUsua fusiUsua;

    public FuncaoSistema() {
    }

    public FuncaoSistema(Integer id) {
        this.id = id;
    }

    public FuncaoSistema(Integer id, String descricao, int ordemExibicao, boolean ativo) {
        this.id = id;
        this.descricao = descricao;
        this.ordemExibicao = ordemExibicao;
        this.ativo = ativo;
    }

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public String getDescricao() {
        return descricao;
    }

    public void setDescricao(String descricao) {
        this.descricao = descricao;
    }

    public int getOrdemExibicao() {
        return ordemExibicao;
    }

    public void setOrdemExibicao(int ordemExibicao) {
        this.ordemExibicao = ordemExibicao;
    }

    public String getUrl() {
        return url;
    }

    public void setUrl(String url) {
        this.url = url;
    }

    public boolean getAtivo() {
        return ativo;
    }

    public void setAtivo(boolean ativo) {
        this.ativo = ativo;
    }

    public FuncaoSistema getFuncaoSistema() {
        return funcaoSistema;
    }

    public void setFuncaoSistema(FuncaoSistema funcaoSistema) {
        this.funcaoSistema = funcaoSistema;
    }

    @XmlTransient
    public Collection<FuncaoSistema> getFuncaoSistemaCollection() {
        return funcaoSistemaCollection;
    }

    public void setFuncaoSistemaCollection(Collection<FuncaoSistema> funcaoSistemaCollection) {
        this.funcaoSistemaCollection = funcaoSistemaCollection;
    }
        
    @XmlTransient
    public List<FusiUsua> getListaFuncaoUsuario() {
        return listaFuncaoUsuario;
    }

    public void setListaFuncaoUsuario(List<FusiUsua> listaFuncaoUsuario) {
        this.listaFuncaoUsuario = listaFuncaoUsuario;
    }
    
    @XmlTransient
    public FusiUsua getFusiUsua() {
        return fusiUsua;
    }

    public void setFusiUsua(FusiUsua fusiUsua) {
        this.fusiUsua = fusiUsua;
    }

    @XmlTransient
    public boolean isSelected() {
        return selected;
    }

    public void setSelected(boolean selected) {
        this.selected = selected;
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
        if (!(object instanceof FuncaoSistema)) {
            return false;
        }
        FuncaoSistema other = (FuncaoSistema) object;
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
