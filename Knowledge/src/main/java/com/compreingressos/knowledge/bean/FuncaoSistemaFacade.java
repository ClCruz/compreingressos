/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.compreingressos.knowledge.bean;

import com.compreingressos.knowledge.model.FuncaoSistema;
import java.util.List;
import javax.ejb.Stateless;
import javax.persistence.EntityManager;
import javax.persistence.PersistenceContext;

/**
 *
 * @author edicarlos.barbosa
 */
@Stateless
public class FuncaoSistemaFacade extends AbstractFacade<FuncaoSistema> {

    @PersistenceContext(unitName = "KnowledgePU")
    private EntityManager em;

    @Override
    protected EntityManager getEntityManager() {
        return em;
    }

    public FuncaoSistemaFacade() {
        super(FuncaoSistema.class);
    }

    @Override
    public List<FuncaoSistema> findAll() {
        return getEntityManager().createNamedQuery("FuncaoSistema.findAll").getResultList();
    }
    
    public List<FuncaoSistema> findPai(Integer usuarioId) {        
        return getEntityManager().createNamedQuery("FuncaoSistema.findPai")
                .setParameter("usuarioId", usuarioId)
                .getResultList();
    }

}
